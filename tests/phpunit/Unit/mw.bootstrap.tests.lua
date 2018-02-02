--[[
	Tests for functions in bootstrap module

	@since 1.1

	@licence GNU GPL v3+
	@author Tobias Oetterer
]]

local testframework = require 'Module:TestFramework'

-- Tests
local tests = {
	{
		name = 'check package load',
		func = function ( args )
			return package.loaded['mw.bootstrap'] == mw.bootstrap
			end,
		args = {},
		expect = { true }
	},
	{
		name = 'check bindings',
		func = function ( args )
				return type( mw.bootstrap )
			end,
		args = {},
		expect = { 'table' }
	},
	{
		name = 'parse function registered and callable',
		func = function ( args )
			local result, returnVal =  pcall( mw.bootstrap[args], '' )
			if result then
				return type( mw.bootstrap[args] ), result
			else
				return type( mw.bootstrap[args] ), result, returnVal
			end
		end,
		args = { 'parse' },
		expect = { 'function', true }
	},
	{
		name = 'getSkin function registered and callable',
		func = function ( args )
			local result, returnVal =  pcall( mw.bootstrap[args], '' )
			if result then
				return type( mw.bootstrap[args] ), result
			else
				return type( mw.bootstrap[args] ), result, returnVal
			end
		end,
		args = { 'getSkin' },
		expect = { 'function', true }
	},
}

return testframework.getTestProvider( tests )
